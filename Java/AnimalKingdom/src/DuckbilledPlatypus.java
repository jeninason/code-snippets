
public class DuckbilledPlatypus extends Mammal {
	
	public static final BirthType PLATYPUS_BIRTH_TYPE = BirthType.LAYS_EGGS;
	public static final String PLATYPUS_DESCRIPTION = "Duckbilled Platypus";
	
	public DuckbilledPlatypus(int id, String name) {
		super(id, name, PLATYPUS_BIRTH_TYPE);
	}
	
	@Override
	public String getDescription() {
		return super.getDescription() + " " + PLATYPUS_DESCRIPTION;
	}

}
