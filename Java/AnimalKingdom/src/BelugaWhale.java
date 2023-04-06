
public class BelugaWhale extends Whale {

	public static final String BELUGA_WHALE_DESCRIPTION = "Beluga Whale";
	
	public BelugaWhale(int id, String name) {
		super(id, name);
	}

	@Override
	public String getDescription() {
		return super.getDescription() + BELUGA_WHALE_DESCRIPTION;
	}

}
